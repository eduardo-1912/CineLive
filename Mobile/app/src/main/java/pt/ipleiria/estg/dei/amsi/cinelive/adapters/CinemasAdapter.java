package pt.ipleiria.estg.dei.amsi.cinelive.adapters;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ItemCinemaBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;

public class CinemasAdapter extends RecyclerView.Adapter<CinemasAdapter.ViewHolder> {

    private final List<Cinema> cinemas;
    private int cinemaSelecionado;
    private final PreferencesManager preferences;

    // Construtor
    public CinemasAdapter(Context context, List<Cinema> cinemas) {
        this.cinemas = cinemas; // Obter lista de cinemas
        preferences = new PreferencesManager(context); // Inicializar preferences
        cinemaSelecionado = preferences.getCinemaId(); // Obter cinema selecionado
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {

        // Criar ViewHolder com Binding (Criar "molde" para depois colocar os dados)
        ItemCinemaBinding binding = ItemCinemaBinding.inflate(
            LayoutInflater.from(parent.getContext()), parent, false
        );

        return new ViewHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        // Para cada posição da lista:
        Cinema cinema = cinemas.get(position); // 1. Vai buscar o objeto cinema
        holder.bind(cinema); // 2. Manda o ViewHolder preencher o layout com os dados do cinema
    }

    @Override
    public int getItemCount() {
        return cinemas.size();
    }

    // O ViewHolder é quem controla o layout de 1 item
    class ViewHolder extends RecyclerView.ViewHolder {

        ItemCinemaBinding binding;

        // Guarda o binding para depois aceder ao item
        public ViewHolder(ItemCinemaBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }

        public void bind(Cinema cinema) {

            // Ver se o cinema atual é o selecionado nas preferences
            boolean isSelected = cinema.getId() == cinemaSelecionado;

            // Preencher com os dados do cinema
            binding.tvNome.setText(cinema.getNome());
            binding.tvMorada.setText(cinema.getMorada());
            binding.tvTelefone.setText(cinema.getTelefone());
            binding.tvEmail.setText(cinema.getEmail());
            binding.tvHorario.setText(cinema.getHorario());
            binding.tvCapacidade.setText(cinema.getCapacidade());

            // Propriedades do botão selecionar cinema
            binding.btnSelecionar.setChecked(isSelected);
            binding.btnSelecionar.setEnabled(!isSelected);
            binding.btnSelecionar.setText(
                isSelected ? R.string.btn_cinema_selecionado : R.string.btn_selecionar_cinema
            );

            // Selecionar um cinema
            binding.btnSelecionar.setOnClickListener(v -> {
                cinemaSelecionado = cinema.getId(); // Atualizar estado interno
                preferences.setCinemaId(cinemaSelecionado); // Guardar nas preferences
                notifyDataSetChanged(); // Atualizar lista
            });
        }
    }
}
