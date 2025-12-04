package pt.ipleiria.estg.dei.amsi.cinelive.adapters;

import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ItemCinemaBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;

public class CinemasAdapter extends RecyclerView.Adapter<CinemasAdapter.ViewHolder> {

    private final List<Cinema> cinemas;
    private int cinemaSelecionado;
    private final OnCinemaClickListener listener;

    public interface OnCinemaClickListener {
        void onCinemaSelected(Cinema cinema);
    }

    public CinemasAdapter(List<Cinema> cinemas, int cinemaSelecionado, OnCinemaClickListener listener) {
        this.cinemas = cinemas;
        this.cinemaSelecionado = cinemaSelecionado;
        this.listener = listener;
    }

    // Atualizar o cinema selecionado
    public void setCinemaSelecionado(int cinemaSelecionado) {
        this.cinemaSelecionado = cinemaSelecionado;
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {

        // Inflate do layout de um item (item_cinema.xml)
        ItemCinemaBinding binding = ItemCinemaBinding.inflate(
                LayoutInflater.from(parent.getContext()), parent, false
        );
        return new ViewHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Cinema cinema = cinemas.get(position);

        // Ver se este cinema é o selecionado
        boolean isSelected = cinema.getId() == cinemaSelecionado;

        // Preencher layout com os dados do cinema
        holder.binding.tvNome.setText(cinema.getNome());
        holder.binding.tvMorada.setText(cinema.getMorada());
        holder.binding.tvTelefone.setText(cinema.getTelefone());
        holder.binding.tvEmail.setText(cinema.getEmail());
        holder.binding.tvHorario.setText(cinema.getHorario());
        holder.binding.tvCapacidade.setText(cinema.getCapacidade());

        // Estado do botão de selecionar
        holder.binding.btnSelecionar.setChecked(isSelected);
        holder.binding.btnSelecionar.setEnabled(!isSelected);
        holder.binding.btnSelecionar.setText(
            isSelected ? R.string.btn_cinema_selecionado : R.string.btn_selecionar_cinema
        );

        // Não permitir selecionar cinemas sem sessões
        if (!cinema.hasSessoes()) {
            holder.binding.btnSelecionar.setEnabled(false);
            holder.binding.btnSelecionar.setChecked(false);
            holder.binding.btnSelecionar.setText(R.string.btn_sem_sessoes_ativas);
        }

        // Botão foi clicado --> avisar o fragment
        holder.binding.btnSelecionar.setOnClickListener(v -> {
            if (listener != null) {
                listener.onCinemaSelected(cinema);
            }
        });
    }

    @Override
    public int getItemCount() {
        return cinemas.size();
    }

    // O ViewHolder representa 1 item da lista
    static class ViewHolder extends RecyclerView.ViewHolder {
        ItemCinemaBinding binding;

        public ViewHolder(ItemCinemaBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}

