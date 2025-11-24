package pt.ipleiria.estg.dei.amsi.cinelive.adapters;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.google.android.material.button.MaterialButton;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;

public class CinemasAdapter extends RecyclerView.Adapter<CinemasAdapter.CinemaViewHolder> {

    private List<Cinema> cinemas;
    private int idCinemaSelecionado;
    private OnCinemaSelecionadoListener listener;

    public interface OnCinemaSelecionadoListener {
        void onCinemaSelecionado(int cinemaId);
    }

    public CinemasAdapter(List<Cinema> cinemas, int cinemaSelecionado, OnCinemaSelecionadoListener listener) {
        this.cinemas = cinemas;
        this.idCinemaSelecionado = cinemaSelecionado;
        this.listener = listener;
    }

    @NonNull
    @Override
    public CinemaViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_cinema, parent, false);
        return new CinemaViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull CinemaViewHolder holder, int position) {
        Cinema cinema = cinemas.get(position);

        holder.nome.setText(cinema.getNome());
        holder.morada.setText(cinema.getMorada());

        // Se este é o cinema selecionado -> botão marcado
        boolean isSelected = cinema.getId() == idCinemaSelecionado;

        holder.btnSelecionar.setChecked(isSelected);
        holder.btnSelecionar.setEnabled(!isSelected);
        holder.btnSelecionar.setText(isSelected
                ? R.string.btn_cinema_selecionado
                : R.string.btn_selecionar_cinema);

        holder.btnSelecionar.setOnClickListener(v -> {
            // atualiza o estado interno
            idCinemaSelecionado = cinema.getId();

            // chama callback para fragment
            if (listener != null) listener.onCinemaSelecionado(cinema.getId());

            // atualiza toda a lista
            notifyDataSetChanged();
        });

    }

    @Override
    public int getItemCount() {
        return cinemas.size();
    }

    static class CinemaViewHolder extends RecyclerView.ViewHolder {
        TextView nome, morada;
        MaterialButton btnSelecionar;

        public CinemaViewHolder(@NonNull View itemView) {
            super(itemView);
            nome = itemView.findViewById(R.id.tvNome);
            morada = itemView.findViewById(R.id.tvMorada);
            btnSelecionar = itemView.findViewById(R.id.btnSelecionar);

        }
    }
}
